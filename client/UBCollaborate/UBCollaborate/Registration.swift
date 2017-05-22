//
//  Registration.swift
//  UBCollaborate
//
//  Created by Onur Tuna on 2017-05-14.
//  Copyright © 2017 Onur Tuna. All rights reserved.
//

import UIKit
import CloudTagView

class Registration: UIViewController, UITextViewDelegate, UIImagePickerControllerDelegate, UINavigationControllerDelegate  {
    
    
    @IBOutlet weak var registration_clubName: UITextField!
    
    @IBOutlet weak var registration_userName: UITextField!
    
    @IBOutlet weak var registration_password: UITextField!
    
    @IBOutlet weak var registration_confirmPassword: UITextField!
    
    @IBOutlet weak var registration_url: UITextField!
    
    @IBOutlet weak var registration_clubLogo: UIImageView!
    
    @IBOutlet weak var registration_clubDescription: UITextView!
    
    @IBOutlet weak var registration_tags: UITextField!
    
    @IBOutlet weak var registration_scrollView: UIScrollView!
    
    @IBOutlet weak var registrationView: UIView!
    
    var jsonData: Any!
    var imageJson: Any!
    
    let color = UIColor(red: 204/255, green: 204/255, blue: 204/255, alpha: 1.0)
    
    let cloudView = CloudTagView()
    
    var tags:String!
    
    
    
    
    // Do any additional setup after loading the view.
    override func viewDidLoad() {
        super.viewDidLoad()
        
        
        registration_clubDescription.resignFirstResponder()

        let viewOrigin = CGPoint(x:(registration_clubDescription.frame.origin.x), y:(registration_tags.frame.origin.y * 9/19))
        
        registration_clubDescription.text = "This is an app to help clubs find another club to collaborate events with."
        registration_clubDescription.textColor = color
        registration_clubDescription.layer.borderColor = color.cgColor
        registration_clubDescription.layer.borderWidth = 0.5
        registration_clubDescription.layer.cornerRadius = 8
        registration_clubDescription.layer.position = viewOrigin
        
        registration_clubDescription.delegate = self
        
        
        textViewDidEndEditing(registration_clubDescription)
        textViewDidBeginEditing(registration_clubDescription)
        
        
        
        registrationView.addSubview(cloudView)
        
        cloudView.translatesAutoresizingMaskIntoConstraints = false
        
        
        
        NSLayoutConstraint.activate([
            
            
            NSLayoutConstraint(item: cloudView, attribute: .top, relatedBy: .equal, toItem: registration_scrollView, attribute: .top, multiplier: 1.0, constant: (registration_tags.frame.origin.y + (registration_tags.frame.origin.y * 9/19))),
            
            NSLayoutConstraint(item: cloudView, attribute: .left, relatedBy: .equal, toItem: registration_scrollView, attribute: .left, multiplier: 1.0, constant: registration_tags.frame.origin.x),
            
            cloudView.heightAnchor.constraint(equalToConstant: 150),
            cloudView.widthAnchor.constraint(equalToConstant: 231)])
        
        
        
        cloudView.layer.borderColor = color.cgColor
        cloudView.layer.borderWidth = 0.5
        cloudView.layer.cornerRadius = 8
        
        
        
        
    }
    
    
    // Dispose of any resources that can be recreated.
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        
    }
    
    
    
    
    func textView(_ textView: UITextView, shouldChangeTextIn range: NSRange, replacementText text: String)-> Bool {
        
        if(text == "\n"){
            textView.resignFirstResponder()
            return false
        }
        return true
    }
    
    
    //Clear Placeholder when start editing
    
    func textViewDidBeginEditing(_ textView: UITextView) {
        
        if(textView == registration_clubDescription && textView.text == "This is an app to help clubs find another club to collaborate events with."){
            textView.text = nil
            textView.textColor = UIColor.black
        }
        textView.becomeFirstResponder()
    }
    
    //Add Placeholder if there is no description
    func textViewDidEndEditing(_ textView: UITextView) {
        
        if(textView == registration_clubDescription && textView.text.isEmpty){
            textView.text = "This is an app to help clubs find another club to collaborate events with."
            textView.textColor = color
        }
        textView.resignFirstResponder()
    }
    
    @IBAction func addLogo(_ sender: UIButton) {
        let imagePickerController = UIImagePickerController()
        imagePickerController.delegate = self
        
        imagePickerController.sourceType = .photoLibrary
        self.present(imagePickerController, animated:true, completion: nil)
    }
    
    //Picking Image from the Photo Library
    
    func imagePickerController(_ picker: UIImagePickerController, didFinishPickingMediaWithInfo info: [String : Any]) {
        let image = info[UIImagePickerControllerOriginalImage] as! UIImage!
        
        registration_clubLogo.image = image
        
        picker.dismiss(animated: true, completion: nil)
    }
    
    func imagePickerControllerDidCancel(_ picker: UIImagePickerController) {
        picker.dismiss(animated: true, completion: nil)
    }
    
    
    //Sending Post Request To Server To register a user
    @IBAction func register(_ sender: Any) {
        
        
        var request = URLRequest(url: URL(string: "https://ryanwirth.ca/misc/ubcollaborate/registration.php")!)
        
        request.httpMethod = "POST"
        
        var logohash = ""
            //logoUpload()
        
        let queryString = "email=\(registration_userName.text!)&password=\(registration_password.text!)&password_verify=\(registration_confirmPassword)&displayname=\(registration_clubName)&description=\(registration_clubDescription)&url=\(registration_url)&tags=\(createTagString())&logohash=\(logohash)"
        
        request.httpBody = queryString.data(using: .utf8)
        
        // check for existence of data
        let task = URLSession.shared.dataTask(with: request){data, response, error in
            guard let data = data, error == nil else{
                print("error: \(error)")
                return
            }
            
            // check for http errors
            if let httpStatus = response as? HTTPURLResponse,
                httpStatus.statusCode != 200 {
                
                print("statusCode should be 200, but is \(httpStatus.statusCode)")
                print("response = \(response)")
            }
            
            
            // return the data as json and store it in a global variable in json format
            self.jsonData = try? JSONSerialization.jsonObject(with: data, options: [])
            print(self.jsonData)
            
            
        }
        task.resume()
        
    }
    
    
    @IBAction func whenPressedReturn(_ sender: Any) {
        
        if cloudView.tags.count >= 5 {
            createTooManyTagsAlert()
            return
        }
        cloudView.tags.append(TagView(text: registration_tags.text!))
        registration_tags.text! = ""
        
        
        
    }
    
    func createTooManyTagsAlert(){
        let tagAlert = UIAlertController(title: "", message: "You cannot enter more than 5 Tags.", preferredStyle: UIAlertControllerStyle.alert)
        
        tagAlert.addAction(UIAlertAction(title: "Ok", style: .default, handler: { (action: UIAlertAction!) in
            
        }))
        
        present(tagAlert, animated: true, completion: nil)
        registration_tags.text! = ""
        
    }
    
    func createTagString()->String {
        
        for i in 0 ..< cloudView.tags.count {
            
            tags.append(cloudView.tags[i].text)
            if(i != cloudView.tags.count) {
                tags.append(",")
            }
        }
        
        return tags
    }
    
    
    
    // sending an image
  /*
    func logoUpload() -> String
    {
        let url = URL(string: "https://ryanwirth.ca/misc/ubcollaborate/registration_upload.php")
        
        let request = NSMutableURLRequest(url: url!)
        request.httpMethod = "POST"
        
        let boundary = generateBoundaryString()
        
        
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
        
        if (registration_clubLogo.image == nil)
        {
            return ""
        }
        
        let image_data = UIImagePNGRepresentation(registration_clubLogo.image!)
        
        
        if(image_data == nil)
        {
            return ""
        }
        
        
        let body = NSMutableData()
        
        let fname = "\(registration_userName).png"
        let mimetype = "\(registration_userName)/png"
        
        
        body.append("--\(boundary)\r\n".data(using: String.Encoding.utf8)!)
        body.append("Content-Disposition:form-data; name=\"test\"\r\n\r\n".data(using: String.Encoding.utf8)!)
        body.append("\(registration_userName)\r\n".data(using: String.Encoding.utf8)!)
        
        
        
        body.append("--\(boundary)\r\n".data(using: String.Encoding.utf8)!)
        body.append("Content-Disposition:form-data; name=\"file\"; filename=\"\(fname)\"\r\n".data(using: String.Encoding.utf8)!)
        body.append("Content-Type: \(mimetype)\r\n\r\n".data(using: String.Encoding.utf8)!)
        body.append(image_data!)
        body.append("\r\n".data(using: String.Encoding.utf8)!)
        
        
        body.append("--\(boundary)--\r\n".data(using: String.Encoding.utf8)!)
        
        
        request.httpBody = body as Data
        
        
        
        let session = URLSession.shared
        
        
        let task = session.dataTask(with: request as URLRequest, completionHandler: {
            (
            data, response, error) in
            
            guard ((data) != nil), let _:URLResponse = response, error == nil else {
                print("error")
                return
            }
            
            // check for http errors
            if let httpStatus = response as? HTTPURLResponse,
                httpStatus.statusCode != 200 {
                
                print("statusCode should be 200, but is \(httpStatus.statusCode)")
                print("response = \(response)")
            }
            
            
            // return the data as json and store it in a global variable in json format
            self.imageJson = try? JSONSerialization.jsonObject(with: data, options: [])
            print(self.jsonData)
            
            

            
        })
        
        task.resume()
        
        
    }
 */
    
    
    func generateBoundaryString() -> String
    {
        return "Boundary-\(UUID().uuidString)"
    }

    
    
    
    
    /*
     // MARK: - Navigation
     
     // In a storyboard-based application, you will often want to do a little preparation before navigation
     override func prepare(for segue: UIStoryboardSegue, sender: Any?) {
     // Get the new view controller using segue.destinationViewController.
     // Pass the selected object to the new view controller.
     }
     */
    
}




